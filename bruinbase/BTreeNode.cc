
#include "BTreeNode.h"
#include <stdlib.h> 
#include <stdio.h>
#include <cstring>
using namespace std;

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
 BTLeafNode::BTLeafNode(){
	 memset(buffer, 0, PageFile::PAGE_SIZE);
 }
  BTNonLeafNode::BTNonLeafNode(){
	 //memset(buffer, 0, PageFile::PAGE_SIZE); crashes for some reason??
	 fill(buffer, buffer+PageFile::PAGE_SIZE,0);
 }
RC BTLeafNode::read(PageId pid, const PageFile& pf)
{ 
	return pf.read(pid, buffer);
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::write(PageId pid, PageFile& pf)
{ 
	return pf.write(pid, buffer);
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTLeafNode::getKeyCount()
{ 
	int count = 0, buffer_key;
	char* buf_iterator = buffer;
	
	for(int i = 0; i < (PageFile::PAGE_SIZE - sizeof(PageId)); 
		i += PAIR_SIZE, buf_iterator += PAIR_SIZE, count++) {
		//examine the current key pair
		memcpy(&buffer_key, buf_iterator, sizeof(int));
		//finish at the last key
		if(buffer_key == 0) {
			return count;
		}
	}
	return count;
}

/*
 * Insert a (key, rid) pair to the node.
 * @param key[IN] the key to insert
 * @param rid[IN] the RecordId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTLeafNode::insert(int key, const RecordId& rid)
{ 
	if(getKeyCount() >= NUM_PAIRS) {
		return RC_NODE_FULL;
	}

	//go through all pairs in buffer till we find a pair with a key greater than key
	char* buf_iterator = buffer;
	int i = 0, buffer_key;
	for(; i < (PageFile::PAGE_SIZE - sizeof(PageId)); i += PAIR_SIZE, buf_iterator += PAIR_SIZE) {
		//examine the current key pair
		memcpy(&buffer_key, buf_iterator, sizeof(int));
		if(buffer_key == 0 || buffer_key >= key) {
			//we have found the spot we need to insert at
			break;
		}
	}

	/** 
	* a)	create an updated buffer and fill it with
	* b)		pairs from pair 0 to pair i
	* c)		pair formed with the parameter key
	* d)		remaining pairs for i+1 to getKeyCount() 
	* e)		save the pageID of the next node
	* f) 		copy the updated buffer over to buffer
	**/

	//part a
	char * updated_buf = (char*) malloc (PageFile::PAGE_SIZE);
	memset(updated_buf, 0, PageFile::PAGE_SIZE);

	//part b
	memcpy(updated_buf, buffer, i);

	//part c
	memcpy(updated_buf+i, &key, sizeof(int));
	memcpy(updated_buf+i+sizeof(int), &rid, sizeof(RecordId));

	//part d
	memcpy(updated_buf+i+PAIR_SIZE, buffer+i, getKeyCount()*PAIR_SIZE - i);

	//part e
	PageId next = getNextNodePtr();
	memcpy(updated_buf+PageFile::PAGE_SIZE-sizeof(PageId), &next, sizeof(PageId));
      
	//part f
	memcpy(buffer, updated_buf, PageFile::PAGE_SIZE);
	free(updated_buf);

	return 0;
}

/*
 * Insert the (key, rid) pair to the node
 * and split the node half and half with sibling.
 * The first key of the sibling node is returned in siblingKey.
 * @param key[IN] the key to insert.
 * @param rid[IN] the RecordId to insert.
 * @param sibling[IN] the sibling node to split with. This node MUST be EMPTY when this function is called.
 * @param siblingKey[OUT] the first key in the sibling node after split.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::insertAndSplit(int key, const RecordId& rid, 
                              BTLeafNode& sibling, int& siblingKey)
{ 
	//make sure sibling is empty 
	// if(sibling.getKeyCount() != 0) {
	// 	return RC_INVALID_ATTRIBUTE;
	// } else {
		memset(&sibling, 0, PageFile::PAGE_SIZE);
	// }

	//make sure splitting is necessary
	if(getKeyCount() < NUM_PAIRS) {
		return RC_INVALID_FILE_FORMAT;
	}

	//first node should have the same number of keys or 1 greater key than second half
	const int FIRST_HALF_INDEX = ((getKeyCount()+1)/2) * PAIR_SIZE;
	const int SECOND_HALF_SIZE = (getKeyCount()/2) * PAIR_SIZE;

	//split up into 2 buffers
	memcpy(sibling.buffer, buffer+FIRST_HALF_INDEX, PageFile::PAGE_SIZE-sizeof(PageId)-FIRST_HALF_INDEX);
	sibling.setNextNodePtr(getNextNodePtr());
	memset(buffer+FIRST_HALF_INDEX, 0, SECOND_HALF_SIZE -sizeof(PageId));

	//get the first key from sibling
	int second_buf_start_key;
	memcpy(&second_buf_start_key, sibling.buffer, sizeof(int));

	//case 1: key needs to be added to buffer
	if(key < second_buf_start_key) {
		insert(key, rid);
	} 
	//case 2: key needs to be added to sibling
	else {
		sibling.insert(key, rid);
	}

	//get the first key from sibling after insertion (in case inserting added a val to index 0)
	memcpy(&siblingKey, sibling.buffer, sizeof(int));

	return 0; 
}

/**
 * If searchKey exists in the node, set eid to the index entry
 * with searchKey and return 0. If not, set eid to the index entry
 * immediately after the largest index key that is smaller than searchKey,
 * and return the error code RC_NO_SUCH_RECORD.
 * Remember that keys inside a B+tree node are always kept sorted.
 * @param searchKey[IN] the key to search for.
 * @param eid[OUT] the index entry number with searchKey or immediately
                   behind the largest key smaller than searchKey.
 * @return 0 if searchKey is found. Otherwise return an error code.
 */
RC BTLeafNode::locate(int searchKey, int& eid)
{ 
	
	char* buf_iterator = buffer;

	for(int i=0; i<getKeyCount()*PAIR_SIZE; i+=PAIR_SIZE, buf_iterator+=PAIR_SIZE)
	{
		int key;
		memcpy(&key, buf_iterator, sizeof(int)); 
		if(key >= searchKey)
		{
			eid = i/PAIR_SIZE;
			return 0;
		}
	}
	
	
	eid = getKeyCount();
	return RC_NO_SUCH_RECORD;
}

/*
 * Read the (key, rid) pair from the eid entry.
 * @param eid[IN] the entry number to read the (key, rid) pair from
 * @param key[OUT] the key from the entry
 * @param rid[OUT] the RecordId from the entry
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::readEntry(int eid, int& key, RecordId& rid)
{ 
	if(eid >= getKeyCount() || eid < 0)
		return RC_NO_SUCH_RECORD;
	memcpy(&key, buffer + eid * PAIR_SIZE, sizeof(int));
	memcpy(&rid, buffer + eid * PAIR_SIZE + sizeof(int) , sizeof(RecordId));
	return 0; 
}

/*
 * Return the pid of the next slibling node.
 * @return the PageId of the next sibling node 
 */
PageId BTLeafNode::getNextNodePtr()
{ 
	//get the value from the end of the buffer
	PageId pid = 0;
	char *buf = buffer;
	memcpy(&pid, buf+PageFile::PAGE_SIZE - sizeof(PageId), sizeof(PageId));
	return pid;
}

/*
 * Set the pid of the next slibling node.
 * @param pid[IN] the PageId of the next sibling node 
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::setNextNodePtr(PageId pid)
{
	//set the value at the end of the buffer
	if(pid < 0) return RC_INVALID_PID;
	char *buf = buffer;
	memcpy(buf+PageFile::PAGE_SIZE-sizeof(PageId), &pid, sizeof(PageId));
	return 0;
}

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::read(PageId pid, const PageFile& pf)
{  
	return pf.read(pid,buffer);
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::write(PageId pid, PageFile& pf)
{ 
	return pf.write(pid,buffer);
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTNonLeafNode::getKeyCount()
{
	int counter = 0;
	char* buf_itr = buffer+8;
	int key;
	for(int i = 8; i < (PageFile::PAGE_SIZE - NONLEAF_PAIR_SIZE); i += NONLEAF_PAIR_SIZE, buf_itr += NONLEAF_PAIR_SIZE, counter++) {
		//examine the current key pair
		memcpy(&key, buf_itr, sizeof(int));
		//finish at the last key
		if(key == 0) {
			return counter;
		}
	}
	return counter;
}


/*
 * Insert a (key, pid) pair to the node.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
 
RC BTNonLeafNode::insert(int key, PageId pid)
{
	//assumes the current page has an empty node and doesn't need to split?
	int pairs = (PageFile::PAGE_SIZE-sizeof(PageId))/NONLEAF_PAIR_SIZE;
	char* buf_itr = buffer+8;
	int search_key;
	int i;
	if(getKeyCount()+1 > pairs){
		return RC_NODE_FULL;
	}
	for(i=8; i<PageFile::PAGE_SIZE-NONLEAF_PAIR_SIZE;i+=NONLEAF_PAIR_SIZE, buf_itr+=NONLEAF_PAIR_SIZE){
		memcpy(&search_key, buf_itr, sizeof(int));
		if(search_key == 0||key<=search_key) break;
	}
	//shift preexisting key pairs to the right.
	char * updated_buf = (char*) malloc (PageFile::PAGE_SIZE);
	memset(updated_buf, 0, PageFile::PAGE_SIZE);
	
	memcpy(updated_buf, buffer, i);
	memcpy(updated_buf+i, &key, sizeof(int));
	memcpy(updated_buf+sizeof(int)+i,&pid,sizeof(PageId));
	memcpy(updated_buf+NONLEAF_PAIR_SIZE+i,buffer+i,getKeyCount()*NONLEAF_PAIR_SIZE+8-i);
	memcpy(buffer,updated_buf,PageFile::PAGE_SIZE);
	free(updated_buf);
	
	//set new key
	
	return 0;
}


/*
 * Insert the (key, pid) pair to the node
 * and split the node half and half with sibling.
 * The middle key after the split is returned in midKey.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @param sibling[IN] the sibling node to split with. This node MUST be empty when this function is called.
 * @param midKey[OUT] the key in the middle after the split. This key should be inserted to the parent node.
 * @return 0 if successful. Return an error code if there is an error.
 * constraint call when leafnode is 1 away from being filled. 
 */
 RC BTNonLeafNode::setNextNodePtr(PageId pid)
{
	//set the value at the end of the buffer
	if(pid < 0) return RC_INVALID_PID;
	char *buf = buffer;
	memcpy(buf+PageFile::PAGE_SIZE-sizeof(PageId), &pid, sizeof(PageId));
	return 0;
}

PageId BTNonLeafNode::getNextNodePtr()
{ 
	//get the value from the end of the buffer
	PageId pid = 0;
	char *buf = buffer;
	memcpy(&pid, buf+PageFile::PAGE_SIZE - sizeof(PageId), sizeof(PageId));
	return pid;
}

RC BTNonLeafNode::insertAndSplit(int key, PageId pid, BTNonLeafNode& sibling, int& midKey)
{ 
	insert(key,pid);
	const int FIRST_HALF_INDEX = ((getKeyCount()+1)/2) * NONLEAF_PAIR_SIZE;
	const int SECOND_HALF_SIZE = (getKeyCount()/2) * NONLEAF_PAIR_SIZE;


	memcpy(sibling.buffer, buffer+FIRST_HALF_INDEX, PageFile::PAGE_SIZE-sizeof(PageId)-FIRST_HALF_INDEX);
	sibling.setNextNodePtr(getNextNodePtr());
	memset(buffer+FIRST_HALF_INDEX, 0, SECOND_HALF_SIZE -sizeof(PageId));
	memcpy(&midKey, buffer+FIRST_HALF_INDEX+sizeof(PageId),sizeof(int));
	return 0;
}


/*
 * Given the searchKey, find the child-node pointer to follow and
 * output it in pid.
 * @param searchKey[IN] the searchKey that is being looked up.
 * @param pid[OUT] the pointer to the child node to follow.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::locateChildPtr(int searchKey, PageId& pid)
{
	int pos,key;
	//fetch key from buffer
	memcpy(&key,buffer+pos*NONLEAF_PAIR_SIZE+sizeof(PageId),sizeof(int));
	//need to iterate through the node
	char* buf_itr = buffer + 8;
	for(int i=8 ; i < getKeyCount()*NONLEAF_PAIR_SIZE+8; i +=NONLEAF_PAIR_SIZE, buf_itr +=8){
		memcpy(&key, buf_itr, sizeof(int));
		if(i==8 && searchKey < key){
			memcpy(&pid, buffer, sizeof(PageId));
			return 0;
		}
		else if(searchKey < key){
			memcpy(&pid, buf_itr-4,sizeof(PageId));
			return 0;
		}
		
	}
	memcpy(&pid, buf_itr-4, sizeof(PageId));
	return 0;
}
/*
 * Initialize the root node with (pid1, key, pid2).
 * @param pid1[IN] the first PageId to insert
 * @param key[IN] the key that should be inserted between the two PageIds
 * @param pid2[IN] the PageId to insert behind the key
 * @return 0 	if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::initializeRoot(PageId pid1, int key, PageId pid2)
{ 
	char* buf = buffer;
	memset(buffer, 0, PageFile::PAGE_SIZE);
	memcpy(buf, &pid1,sizeof(PageId));
	RC error = insert(key,pid2);
	return error;
}
