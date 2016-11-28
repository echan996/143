/*
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */
 
#include "BTreeIndex.h"
#include "BTreeNode.h"

using namespace std;

/*
 * BTreeIndex constructor
 */
BTreeIndex::BTreeIndex()
{
    rootPid = -1;
	treeHeight = 0;
	std::fill(buffer, buffer + PageFile::PAGE_SIZE, 0);
}

/*
 * Open the index file in read or write mode.
 * Under 'w' mode, the index file should be created if it does not exist.
 * @param indexname[IN] the name of the index file
 * @param mode[IN] 'r' for read, 'w' for write
 * @return error code. 0 if no error
 */
RC BTreeIndex::open(const string& indexname, char mode)
{
	RC error_code = pf.open(indexname, mode);
	if(error_code) return error_code;

	if(pf.endPid() == 0) {
		rootPid = -1;
		treeHeight = 0;
		return 0;
	}

	error_code = pf.read(0, buffer);
	if(error_code) return error_code;

	int temp_pid, temp_height;
	memcpy(&temp_pid, buffer, sizeof(PageId));
	memcpy(&temp_height, buffer + sizeof(PageId), sizeof(int));

	if(temp_height>0 && temp_pid>0) {
		rootPid = temp_pid;
		treeHeight = temp_height;
	}

  return 0;
}

/*
 * Close the index file.
 * @return error code. 0 if no error
 */
RC BTreeIndex::close()
{
	memcpy(buffer, &rootPid, sizeof(int));
	memcpy(buffer + sizeof(int), &treeHeight, sizeof(int));
	
	RC error_code = pf.write(0, buffer);
	if(error_code) return error_code;

  return pf.close();
}

/*
 * Insert (key, RecordId) pair to the index.
 * @param key[IN] the key for the value inserted into the index
 * @param rid[IN] the RecordId for the record being inserted into the index
 * @return error code. 0 if no error
 */
RC BTreeIndex::insert(int key, const RecordId& rid)
{
    //top level creation logic and recursive call 
	
	if(treeHeight==0){
		//create tree
		BTLeafNode rootNode;
		rootNode.insert(key,rid);
		if(pf.endPid()==0)
			rootPid = 1;
		else
			rootPid = pf.endPid();
		treeHeight++;
		int error = rootNode.write(rootPid,pf);
		//bug checking on error codes?
		return error;
	}
	//else traverse the tree!
	PageId temp_pid=-1;
	int temp_key= -1;
	return sub_insert(key,rid,rootPid,1,temp_pid, temp_key);
}

RC BTreeIndex::sub_insert(int key, const RecordId& rid, PageId pid, int height, PageId& temp_pid, int& temp_key){
	RC error;
	temp_pid=-1;
	temp_key=-1;
	//middle of tree condition
	if(height!=treeHeight){
		BTNonLeafNode node;
		node.read(pid, pf);
		PageId location=0;
		node.locateChildPtr(key,location);
		PageId recursePid=-1;
		int recurseKey=-1;
		error = sub_insert(key,rid,location,height+1,recursePid,recurseKey);
		
		if(recursePid!=-1 || recurseKey!=-1){
			if(node.insert(recurseKey,recursePid)==0){
				node.write(pid,pf);
				return 0;
			}
			else{ //need to split
				BTNonLeafNode node2;
				int key2;
				node.insertAndSplit(recurseKey,recursePid,node2,key2);
				//pass key to parent and write to child
				temp_pid = pf.endPid();
				temp_key = key2;
				error = node.write(pid,pf);
				if(error)
					return error;
				error = node2.write(temp_pid,pf);
				if(error)
					return error;
				
				//check root?
				if(treeHeight==1){
					BTNonLeafNode root;
					root.initializeRoot(pid,temp_key,temp_pid);
					treeHeight++;
					rootPid=pf.endPid();
					root.write(rootPid,pf);
				}
			}
			return 0;
		}
	}
	else { //youre at the bottom of the tree dealing with leaf nodes!
		// cout << "enters leaf insert" << endl;
		BTLeafNode leaf;
		leaf.read(pid,pf);
		if(leaf.insert(key,rid)==0){
			// cout << "leaf inserted" << endl;
			leaf.write(pid,pf);
			return 0;
		}
		// otherwise, we have to insertAndSplit
		BTLeafNode newLeaf;
		int newKey;
		error = leaf.insertAndSplit(key,rid,newLeaf,newKey);
		if(error)
			return error;
		// cout << "leaf insert and split works" << endl;
		
		temp_key = newKey;
		temp_pid = pf.endPid();
		
		newLeaf.setNextNodePtr(leaf.getNextNodePtr());
		leaf.setNextNodePtr(temp_pid);
		
		
		//write
		error = leaf.write(pid,pf);
		if(error)
			return error;
		error = newLeaf.write(temp_pid,pf);
		if(error)
			return error;
		
		
		if(treeHeight==1){
			BTNonLeafNode root;
			root.initializeRoot(pid,temp_key,temp_pid);
			treeHeight++;
			rootPid=pf.endPid();
			root.write(rootPid,pf);
		}
		return 0;
	}
	return 0;
}

/**
 * Run the standard B+Tree key search algorithm and identify the
 * leaf node where searchKey may exist. If an index entry with
 * searchKey exists in the leaf node, set IndexCursor to its location
 * (i.e., IndexCursor.pid = PageId of the leaf node, and
 * IndexCursor.eid = the searchKey index entry number.) and return 0.
 * If not, set IndexCursor.pid = PageId of the leaf node and
 * IndexCursor.eid = the index entry immediately after the largest
 * index key that is smaller than searchKey, and return the error
 * code RC_NO_SUCH_RECORD.
 * Using the returned "IndexCursor", you will have to call readForward()
 * to retrieve the actual (key, rid) pair from the index.
 * @param key[IN] the key to find
 * @param cursor[OUT] the cursor pointing to the index entry with
 *                    searchKey or immediately behind the largest key
 *                    smaller than searchKey.
 * @return 0 if searchKey is found. Othewise an error code
 */

 
RC BTreeIndex::locate(int searchKey, IndexCursor& cursor)
{
	BTLeafNode leaf;
	BTNonLeafNode mid;
	RC error_code;
	PageId pid = rootPid;
	int eid;

	for(int cur = 1; cur < treeHeight; cur++) {
		error_code = mid.read(pid, pf);
		if(error_code) return error_code;

		error_code = mid.locateChildPtr(searchKey, pid);
		if(error_code) return error_code;
	}

	error_code = leaf.read(pid, pf);
	if(error_code) return error_code;

	error_code = leaf.locate(searchKey, eid);
	if(error_code) return error_code;

	cursor.eid = eid;
	cursor.pid = pid;

  return 0;
}

/*
 * Read the (key, rid) pair at the location specified by the index cursor,
 * and move foward the cursor to the next entry.
 * @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
 * @param key[OUT] the key stored at the index cursor location.
 * @param rid[OUT] the RecordId stored at the index cursor location.
 * @return error code. 0 if no error
 */
RC BTreeIndex::readForward(IndexCursor& cursor, int& key, RecordId& rid)
{
	RC error_code;
	BTLeafNode l;

	error_code = l.read(cursor.pid, pf);
	if(error_code) return error_code;

	if(cursor.pid <= 0)
		return RC_INVALID_CURSOR;

	error_code = l.readEntry(cursor.eid, key, rid);
	if(error_code) return error_code;

	if(l.getKeyCount() >= cursor.eid) cursor.eid++;
	else {
		cursor.eid = 0;
		cursor.pid = l.getNextNodePtr();
	}

  return 0;
}
